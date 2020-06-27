library(stringr)
library(dplyr)
library(docstring)

concat_colors_by_beta <- function(beta_column, p_column) {
  res <- ifelse(as.numeric(beta_column) >=0 & as.numeric(p_column) <= 0.001, paste0("\\cellcolor{blue!40} ",beta_column),
         ifelse(as.numeric(beta_column) >=0 & as.numeric(p_column) <=0.01, paste0("\\cellcolor{blue!25} ",beta_column),
         ifelse(as.numeric(beta_column) >=0 & as.numeric(p_column) <=0.05, paste0("\\cellcolor{blue!10} ",beta_column),
         ifelse(as.numeric(beta_column) >=0 & as.numeric(p_column) <=0.1, paste0("\\cellcolor{blue!5} ",beta_column),
         ifelse(as.numeric(beta_column) <0 & as.numeric(p_column) <= 0.001, paste0("\\cellcolor{red!40} ",beta_column),
         ifelse(as.numeric(beta_column) <0 & as.numeric(p_column) <=0.01, paste0("\\cellcolor{red!25} ",beta_column),
         ifelse(as.numeric(beta_column) <0 & as.numeric(p_column) <=0.05, paste0("\\cellcolor{red!10} ",beta_column),
         ifelse(as.numeric(beta_column) <0 & as.numeric(p_column) <=0.1, paste0("\\cellcolor{red!5} ",beta_column), beta_column))))))))
  return (res)
}

concat_colors_by_p <- function(beta_column, p_column) {
  res <- ifelse(as.numeric(beta_column) >=0 & as.numeric(p_column) <= 0.001, paste0("\\cellcolor{blue!40} ",p_column," & \\cellcolor{blue!40}***"),
         ifelse(as.numeric(beta_column) >=0 & as.numeric(p_column) <=0.01, paste0("\\cellcolor{blue!25} ",p_column," & \\cellcolor{blue!25}**"),
         ifelse(as.numeric(beta_column) >=0 & as.numeric(p_column) <=0.05, paste0("\\cellcolor{blue!10} ",p_column," & \\cellcolor{blue!10}*"),
         ifelse(as.numeric(beta_column) >=0 & as.numeric(p_column) <=0.1, paste0("\\cellcolor{blue!5} ",p_column," & \\cellcolor{blue!5}!"),
         ifelse(as.numeric(beta_column) <0 & as.numeric(p_column) <= 0.001, paste0("\\cellcolor{red!40} ",p_column," & \\cellcolor{red!40}***"),
         ifelse(as.numeric(beta_column) <0 & as.numeric(p_column) <=0.01, paste0("\\cellcolor{red!25} ",p_column," & \\cellcolor{red!25}**"),
         ifelse(as.numeric(beta_column) <0 & as.numeric(p_column) <=0.05, paste0("\\cellcolor{red!10} ",p_column," & \\cellcolor{red!10}*"),
         ifelse(as.numeric(beta_column) <0 & as.numeric(p_column) <=0.1, paste0("\\cellcolor{red!5} ",p_column," & \\cellcolor{red!5}!"), 
                paste0(p_column," & ")))))))))
  return (res)
}

generate_latex_table <- function(title="Default title", caption="Default caption", df, type=FALSE){
  #' @family regression_report
  #' @title Generate a Latex Table
  #'
  #' @description Generate a regression table for Latex on the fly.. because it is so.. tedious.
  #'
  #' @param title the title to be abbreviated in the label.
  #' @param caption the caption to be shown in the table.
  #' @param df a data frame that has columns: c("time_lag", "dv", "iv", "beta_iv", "p_iv", "beta_legitimation", 
  #'                                 "p_legit", "beta_competition", "p_comp", "df", "t", "REML")
  #' @param type boolean on whether the regression models are Bayesian or not (default=False)
  #' @return Latex table scripts w/ color codings + p-value significance (asteriks)
  #'
  
  n_dv <- length(levels(as.factor(df$dv)))
 
  base_texts <- paste0("\\begin{landscape} \n \\begin{table}[htbp]	\n
		\\centering \n
		\\caption{", caption, "}\\label{tab:", abbreviate(title), "} \n
		\\resizebox{\\linewidth}{!}{  \n
  \\begin{tabular}{@{\\extracolsep{3pt}}l") 
  
  # counting the number of models
  n_time_lag <- length(levels(as.factor(df$time_lag)))
  n_iv <- length(levels(as.factor(df$iv)))
  n <- (n_time_lag * n_iv)
  
  # beta, p-value, and asterisk
  for (i in 1:n){
    base_texts <- paste0(base_texts, "r@{ }r@{ }l")
    
    if (i %% n_time_lag == 0 & i < n){
      base_texts <- paste0(base_texts, "|")
    }
  }
  
  base_texts <- paste0(base_texts, "}\\\\ \n")

  
  base_texts <- paste0(base_texts, " \\hline \n")
  
  # columns for IV names
  l = 1
  
  for (iv in levels(as.factor(df$iv))){
    if (l < n_iv){
      base_texts <- paste0(base_texts, "&\\multicolumn{", as.character(3*n_time_lag), "}{c|}{", as.character(iv), "} \n")
    } else {
      base_texts <- paste0(base_texts, "&\\multicolumn{", as.character(3*n_time_lag), "}{c}{", as.character(iv), "}\\\\ \n")
    }
    l <- l+1
  }
  
  base_texts <- paste0(base_texts, "\\hline \n")
  
  # columns for time lags
  l = 1
  
  for (iv in levels(as.factor(df$iv))){
    for (i in 1:n_time_lag){
      if (i %% n_time_lag == 0 & l < n){
        base_texts <- paste0(base_texts, "&\\multicolumn{3}{c|}{Lag: ", as.character(i-1) ,"} \n")
      } else {
        base_texts <- paste0(base_texts, "&\\multicolumn{3}{c}{Lag: ", as.character(i-1) ,"} \n")
      }
      l <- l+1
    }
  }
  base_texts <- paste0(base_texts, "\\\\ ")
  
  
  # looping though the followings for the number of DVs times?
  
  for (dv in levels(as.factor(df$dv))){
    # clines for dividing the results section
    
    for (i in 1:n){
      base_texts <- paste0(base_texts, "\\cline{", as.character(3*i-1), "-", as.character(3*i+1), "} ")
    }
    base_texts <- paste0(base_texts, "\n", dv)
    
    # Column titles for the three columns: beta, p, and significance
    for (i in 1:n){
      if (type==TRUE){
        base_texts <- paste0(base_texts, "&Est. &CI 5\\% & CI 95\\%")  
      } else {
        base_texts <- paste0(base_texts, "&Est. &$p$ & ")  
      }
    }
    base_texts <- paste0(base_texts, "\\\\ \n \\hline \n")
    
    # Making Latex cells out of the data frame
    tmp <- df[df$dv == dv,]
    tmp$beta_iv <- format(round(tmp$beta_iv, 3), nsmall=3)
    tmp$p_iv <- format(round(tmp$p_iv, 3), nsmall=3)
    tmp$beta_legitimation <- format(round(tmp$beta_legitimation, 3), nsmall=3)
    tmp$p_legit <- format(round(tmp$p_legit, 3), nsmall=3)
    tmp$beta_competition <- format(round(tmp$beta_competition, 3), nsmall=3)
    tmp$p_comp <- format(round(tmp$p_comp, 3), nsmall=3)
    
    if (type != TRUE){
      beta_iv <- concat_colors_by_beta(tmp$beta_iv, tmp$p_iv)
      p_iv <- concat_colors_by_p(tmp$beta_iv, tmp$p_iv)
      beta_legit <- concat_colors_by_beta(tmp$beta_legitimation, tmp$p_legit)
      p_legit <- concat_colors_by_p(tmp$beta_legitimation, tmp$p_legit)
      beta_comp <- concat_colors_by_beta(tmp$beta_competition, tmp$p_comp)
      p_comp <- concat_colors_by_p(tmp$beta_competition, tmp$p_comp)
      
      tmp$beta_iv <- beta_iv
      tmp$p_iv <- p_iv
      tmp$beta_legitimation <- beta_legit
      tmp$p_legit <- p_legit
      tmp$beta_competition <- beta_comp
      tmp$p_comp <- p_comp
    }
    
    tmp <- tmp[order(tmp$time_lag),]
    tmp <- tmp[order(tmp$iv),]
    tmp <- t(tmp[,4:9])
    tmp <- as.data.frame(tmp)
    tmp %<>% mutate_if(is.factor,as.character)
    
    for (r in 1:(nrow(tmp)/2)){
      
      base_texts <- paste0(base_texts, "\\hspace*{5mm} ")
      if (r==1) base_texts <- paste0(base_texts, "Fuzziness &")
      else if (r==2) base_texts <- paste0(base_texts, "Legitimation &")
      else if (r==3) base_texts <- paste0(base_texts, "Competition &")
      
      for (c in 1:ncol(tmp)){
        if (c != ncol(tmp)) base_texts <- paste0(base_texts, tmp[2*r-1,c], " & ", tmp[2*r,c], " & ") 
        else base_texts <- paste0(base_texts, tmp[2*r-1,c], " & ", tmp[2*r,c], "\n") 
      }
      base_texts <- paste0(base_texts, "\\\\ \n")
    }
    
    base_texts <- paste0(base_texts, "\\hline \n")
    base_texts <- paste0(base_texts, "\\\\ \n")
  }
  
  base_texts <- paste0(base_texts, "\\hline \n  \\end{tabular} \n }
	\\end{table} \n
  \\end{landscape}")
  
  return(base_texts)
}




# Run stan_lmer and generate the Latex output
run_stan_lmer <- function(h_title, num_city, num_data, rhs, data,
                          dv1, dv2=NULL, dv3=NULL, dv4=NULL, dv5=NULL){
  dv_count <- 1
  
  model_1 <- paste0("scale(", dv1 ,") ~", rhs) %>% as.formula
  fit1 <- stan_lmer(model_1, data=data)  
  
  if (!is.null(dv2)) {
    print("Fitting 2nd DV...")
    model_2 <- paste0("scale(", dv2 ,") ~", rhs) %>% as.formula
    fit2 <-  stan_lmer(model_2, data=data)  
    dv_count <- dv_count + 1
  } else {fit2=NULL}
  if (!is.null(dv3)) {
    print("Fitting 3rd DV...")
    model_3 <- paste0("scale(", dv3 ,") ~", rhs) %>% as.formula
    fit3 <- stan_lmer(model_3, data=data)  
    dv_count <- dv_count + 1
  } else {fit3=NULL}
  if (!is.null(dv4)) {
    print("Fitting 4th DV...")
    model_4 <- paste0("scale(", dv4 ,") ~", rhs) %>% as.formula
    fit4 <- stan_lmer(model_4, data=data)  
    dv_count <- dv_count + 1
  } else {fit4=NULL}
  if (!is.null(dv5)) {
    print("Fitting 5th DV...")
    model_5 <- paste0("scale(", dv5 ,") ~", rhs) %>% as.formula
    fit5 <- stan_lmer(model_5, data=data)  
    dv_count <- dv_count + 1
  } else {fit5=NULL}
  
  p <- generate_latex_table(paste0(h_title, ": ", as.character(num_city),
                              "Cities ($N=", as.character(num_data),"$)"), "b", fit1,fit2,fit3,fit4,fit5)
  return(p)
}
